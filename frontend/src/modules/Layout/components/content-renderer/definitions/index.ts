// Structure
import Section from './Section';
import Container from './Container';
import Columns from './Columns';
import Row from './Row';
import Column from './Column';

// Basic
import Heading from './Heading';
import RichText from './RichText';
import Text from './Text';
import Button from './Button';
import Icon from './Icon';
import Divider from './Divider';
import Spacer from './Spacer';
import Html from './Html';

// Media
import Image from './Image';
import Gallery from './Gallery';
import Video from './Video';
import Audio from './Audio';
import Slider from './Slider';
import Map from './Map';
import MapPin from './MapPin';
import VideoPopup from './VideoPopup';
import Embed from './Embed';
import Lottie from './Lottie';
import BeforeAfter from './BeforeAfter';

// Content
import Hero from './Hero';
import CTA from './CTA';
import FeatureGrid from './FeatureGrid';
import Quote from './Quote';
import Testimonial from './Testimonial';
import Blurb from './Blurb';
import Author from './Author';
// Imports updated
import TeamMember from './TeamMember';

import Alert from './Alert';
import IconList from './IconList';
import SocialLinks from './SocialLinks';
import Code from './Code';
import ContactForm from './ContactForm';
import ContactField from './ContactField';
import Search from './Search';
import Breadcrumbs from './Breadcrumbs';
import TableOfContents from './TableOfContents';
import ShareButtons from './ShareButtons';
import PricingTable from './PricingTable';
import ProgressBar from './ProgressBar';
import CircleCounter from './CircleCounter';
import Counter from './Counter';
import Countdown from './Countdown';
import Newsletter from './Newsletter';
import Signup from './Signup';
import Portfolio from './Portfolio';
import FilterablePortfolio from './FilterablePortfolio';

import Accordion from './Accordion';
import Tabs from './Tabs';
import Toggle from './Toggle';
import Menu from './Menu';
import Sidebar from './Sidebar';
import Login from './Login';
import Logo from './Logo';
import Group from './Group';
import GroupCarousel from './GroupCarousel';
import Feature from './Feature';
import NumberBox from './NumberBox';
import NumberCounter from './NumberCounter';
import PricingFeature from './PricingFeature';

import Blog from './Blog';
import PostContent from './PostContent';
import PostTitle from './PostTitle';
import FeaturedImage from './FeaturedImage';
import PostMeta from './PostMeta';
import PostNavigation from './PostNavigation';
import TabbedPosts from './TabbedPosts';
import PostSlider from './PostSlider';
import RelatedPosts from './RelatedPosts';
import Comments from './Comments';

import StarRating from './StarRating';
import FAQ from './FAQ';
import LogoGrid from './LogoGrid';
import VideoSlider from './VideoSlider';
import FullwidthSlider from './FullwidthSlider';
import FullwidthSlideItem from './FullwidthSlideItem';
import FullwidthHeader from './FullwidthHeader';
import FullwidthPortfolio from './FullwidthPortfolio';
import FullwidthCode from './FullwidthCode';
import FullwidthImage from './FullwidthImage';
import FullwidthMap from './FullwidthMap';
import FullwidthMenu from './FullwidthMenu';
import FullwidthPostContent from './FullwidthPostContent';
import FullwidthPostSlider from './FullwidthPostSlider';
import FullwidthPostTitle from './FullwidthPostTitle';

// Form Builder
import FormInput from './FormInput';
import FormTextarea from './FormTextarea';
import FormSelect from './FormSelect';
import FormCheckbox from './FormCheckbox';
import FormRadio from './FormRadio';
import FormPicker from './FormPicker';
import FormStep from './FormStep';
import FormProgressBar from './FormProgressBar';

import type { BlockDefinition } from '@/types/builder';

export default [
    // Structure
    Section,
    Container,
    Columns,
    Row,
    Column,

    // Basic
    Heading,
    RichText,
    Text,
    Button,
    Icon,
    Divider,
    Spacer,
    Html,

    // Media
    Image,
    Gallery,
    Video,
    Audio,
    Slider,
    Map,
    MapPin,
    Lottie,
    BeforeAfter,
    VideoPopup,
    Embed,

    // Content
    Hero,
    CTA,
    FeatureGrid,
    Quote,
    Testimonial,
    Blurb,
    Author,
    TeamMember,
    Alert,
    IconList,
    SocialLinks,
    Code,
    ContactForm,
    ContactField,
    Search,
    Breadcrumbs,
    TableOfContents,
    ShareButtons,
    PricingTable,
    PricingFeature,
    ProgressBar,
    CircleCounter,
    Counter,
    Countdown,
    Newsletter,
    Signup,
    Portfolio,
    FilterablePortfolio,

    // Layout
    Accordion,
    Tabs,
    Toggle,
    Menu,
    Sidebar,
    Login,
    Logo,
    Group,
    GroupCarousel,
    Feature,
    NumberBox,
    NumberCounter,

    // Blog
    Blog,
    PostContent,
    PostTitle,
    FeaturedImage,
    PostMeta,
    PostNavigation,
    TabbedPosts,
    PostSlider,
    RelatedPosts,
    Comments,

    StarRating,
    FAQ,
    LogoGrid,
    VideoSlider,
    FullwidthSlider,
    FullwidthSlideItem,
    FullwidthHeader,
    FullwidthPortfolio,
    FullwidthCode,
    FullwidthImage,
    FullwidthMap,
    FullwidthMenu,
    FullwidthPostContent,
    FullwidthPostSlider,
    FullwidthPostTitle,

    // Form Builder
    FormInput,
    FormTextarea,
    FormSelect,
    FormCheckbox,
    FormRadio,
    FormPicker,
    FormStep,
    FormProgressBar
] as BlockDefinition[];
